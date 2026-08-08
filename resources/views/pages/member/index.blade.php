@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Member" />

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{
            modalHapusTerbuka: false,
            formTerpilih: null,
            namaHapus: '',
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
            }
        }">

        {{-- Header --}}
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between p-6 border-b border-gray-100 dark:border-gray-800">

            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Daftar Member
            </h3>

            @can('tambah_member')
                <a href="{{ route('member.create') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>

                    <span>Tambah Member</span>
                </a>
            @endcan
        </div>


        {{-- Table --}}
        <div class="overflow-x-auto p-6">
            <table class="w-full text-left text-sm">

                <thead>
                    <tr class="border-b border-gray-100 text-gray-500 dark:border-gray-800 dark:text-gray-400">

                        {{-- Mobile expand --}}
                        <th class="py-3 px-2 sm:hidden w-8 text-center"></th>

                        <th class="py-3">
                            Nama
                        </th>

                        <th class="py-3 hidden md:table-cell">
                            No. HP
                        </th>

                        <th class="py-3 hidden lg:table-cell">
                            Email
                        </th>

                        <th class="py-3 hidden lg:table-cell">
                            Tanggal Lahir
                        </th>

                        <th class="py-3 hidden sm:table-cell">
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
                        $levelClasses = match ($member->level) {
                            'Gold' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
                            'Silver' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300',
                            default => 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400',
                        };
                    @endphp

                    <tbody
                        x-data="{ open: false }"
                        class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">

                        {{-- Main row --}}
                        <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition">

                            {{-- Expand button mobile --}}
                            <td class="py-3.5 px-2 sm:hidden text-center">

                                <button
                                    @click="open = !open"
                                    type="button"
                                    class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-xs font-bold transition-transform duration-200"
                                    :class="open ? 'rotate-90 bg-amber-500 text-white' : ''">

                                    <span x-text="open ? '−' : '+'"></span>

                                </button>

                            </td>


                            {{-- Nama --}}
                            <td class="py-3.5 font-medium text-gray-800 dark:text-white/90">
                                {{ $member->nama }}
                            </td>


                            {{-- No HP --}}
                            <td class="py-3.5 hidden md:table-cell text-gray-600 dark:text-gray-400">
                                {{ $member->no_hp }}
                            </td>


                            {{-- Email --}}
                            <td class="py-3.5 hidden lg:table-cell text-gray-600 dark:text-gray-400">
                                {{ $member->email ?: '-' }}
                            </td>


                            {{-- Tanggal lahir --}}
                            <td class="py-3.5 hidden lg:table-cell text-gray-600 dark:text-gray-400">
                                {{ $member->tanggal_lahir ? $member->tanggal_lahir->format('d/m/Y') : '-' }}
                            </td>


                            {{-- Poin --}}
                            <td class="py-3.5 hidden sm:table-cell text-gray-600 dark:text-gray-400">
                                {{ $member->poin }}
                            </td>


                            {{-- Level --}}
                            <td class="py-3.5">

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-medium {{ $levelClasses }}">

                                    {{ $member->level }}

                                </span>

                            </td>


                            {{-- Aksi --}}
                            <td class="py-3.5 text-right space-x-1.5 whitespace-nowrap">

                                @can('edit_member')
                                    <a
                                        href="{{ route('member.edit', $member) }}"
                                        class="text-amber-600 dark:text-amber-400 font-semibold hover:underline text-xs sm:text-sm">

                                        Edit

                                    </a>
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
                                                '{{ addslashes($member->nama) }}'
                                            )"
                                            class="text-red-500 font-semibold hover:underline text-xs sm:text-sm">

                                            Hapus

                                        </button>

                                    </form>

                                @endcan

                            </td>

                        </tr>


                        {{-- Expandable row untuk mobile --}}
                        <tr
                            x-show="open"
                            x-cloak
                            x-transition
                            class="sm:hidden bg-amber-50/50 dark:bg-amber-950/30 text-xs">

                            <td colspan="6" class="p-3.5">

                                <div class="grid grid-cols-2 gap-2.5 text-gray-600 dark:text-gray-300">

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
                                            {{ $member->poin }}
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


                                    {{-- Tanggal lahir --}}
                                    <div>

                                        <span class="text-gray-400">
                                            Tanggal Lahir:
                                        </span>

                                        <span class="font-semibold text-gray-800 dark:text-white">
                                            {{ $member->tanggal_lahir ? $member->tanggal_lahir->format('d/m/Y') : '-' }}
                                        </span>

                                    </div>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                @endforeach

            </table>
        </div>


        {{-- Modal konfirmasi hapus --}}
        <div
            x-show="modalHapusTerbuka"
            x-cloak
            x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">

            <div
                @click.away="modalHapusTerbuka = false"
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100 dark:border-gray-800">

                <div class="p-6 text-center">

                    <div
                        class="w-14 h-14 bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">

                        ⚠

                    </div>

                    <h3 class="font-extrabold text-gray-900 dark:text-white text-base">
                        Hapus Member?
                    </h3>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">

                        Member
                        <span
                            class="font-semibold text-gray-700 dark:text-gray-300"
                            x-text="namaHapus">
                        </span>

                        akan dihapus dan tidak dapat dipulihkan kembali.

                    </p>

                </div>


                <div
                    class="bg-gray-50 dark:bg-gray-950 p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center gap-3">

                    <button
                        type="button"
                        @click="modalHapusTerbuka = false"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition text-gray-700 dark:text-gray-300">

                        Batal

                    </button>


                    <button
                        type="button"
                        @click="konfirmasiHapus()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs">

                        Hapus

                    </button>

                </div>

            </div>

        </div>

    </div>

@endsection