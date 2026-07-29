@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Tambah Aturan Member" />

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    <form action="{{ route('pengaturan-member.store') }}" method="POST">
        @csrf

        <div class="p-6 space-y-6">

            {{-- Nama Aturan --}}
            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Nama Aturan
                </label>

                <input
                    type="text"
                    name="nama_aturan"
                    class="w-full rounded-xl border border-amber-200 bg-amber-50/40
                           px-4 py-2.5
                           text-gray-800 dark:text-white
                           placeholder-gray-400
                           focus:border-amber-500 focus:ring-2 focus:ring-amber-200
                           dark:bg-gray-900 dark:border-gray-700
                           transition duration-200"
                    required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Minimal Belanja --}}
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Belanja Setiap (Rp)
                    </label>

                    <input
                        type="number"
                        name="minimal_belanja"
                        class="w-full rounded-xl border border-amber-200 bg-amber-50/40
                               px-4 py-2.5
                               text-gray-800 dark:text-white
                               focus:border-amber-500 focus:ring-2 focus:ring-amber-200
                               dark:bg-gray-900 dark:border-gray-700
                               transition duration-200"
                        required>
                </div>

                {{-- Poin --}}
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Poin Didapat
                    </label>

                    <input
                        type="number"
                        name="poin"
                        class="w-full rounded-xl border border-amber-200 bg-amber-50/40
                               px-4 py-2.5
                               text-gray-800 dark:text-white
                               focus:border-amber-500 focus:ring-2 focus:ring-amber-200
                               dark:bg-gray-900 dark:border-gray-700
                               transition duration-200"
                        required>
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Mulai --}}
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Mulai Berlaku
                    </label>

                    <input
                        type="datetime-local"
                        name="mulai_berlaku"
                        class="w-full rounded-xl border border-amber-200 bg-amber-50/40
                               px-4 py-2.5
                               text-gray-800 dark:text-white
                               focus:border-amber-500 focus:ring-2 focus:ring-amber-200
                               dark:bg-gray-900 dark:border-gray-700
                               transition duration-200"
                        required>
                </div>

                {{-- Selesai --}}
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Selesai Berlaku
                    </label>

                    <input
                        type="datetime-local"
                        name="selesai_berlaku"
                        class="w-full rounded-xl border border-amber-200 bg-amber-50/40
                               px-4 py-2.5
                               text-gray-800 dark:text-white
                               focus:border-amber-500 focus:ring-2 focus:ring-amber-200
                               dark:bg-gray-900 dark:border-gray-700
                               transition duration-200">
                </div>

            </div>

        </div>

        <div class="flex justify-end gap-3 p-6 border-t border-gray-100 dark:border-gray-800">

            <a href="{{ route('pengaturan-member.index') }}"
               class="px-5 py-2.5 rounded-xl
                      border border-amber-200
                      bg-amber-50 hover:bg-amber-100
                      text-amber-800 font-medium
                      transition
                      dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                Batal
            </a>

            <button
                type="submit"
                class="px-5 py-2.5 rounded-xl
                       bg-amber-700 hover:bg-amber-800
                       text-white font-semibold
                       transition shadow-sm">
                Simpan
            </button>

        </div>

    </form>

</div>

@endsection