@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Pengaturan Member" />

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Header --}}
    <div class="flex items-center justify-between p-6 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Pengaturan Sistem Poin Member
        </h3>
    </div>

    <form action="{{ route('pengaturan-member.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-6">

            {{-- Status --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Status Sistem
                </label>

                <select
                    name="aktif"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">

                    <option value="1" {{ $pengaturan->aktif ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option value="0" {{ !$pengaturan->aktif ? 'selected' : '' }}>
                        Nonaktif
                    </option>

                </select>
            </div>

            {{-- Minimal Belanja --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Minimal Belanja (Rp)
                </label>

                <input
                    type="number"
                    name="minimal_belanja"
                    value="{{ old('minimal_belanja',$pengaturan->minimal_belanja) }}"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            {{-- Poin --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Poin
                </label>

                <input
                    type="number"
                    name="poin"
                    value="{{ old('poin',$pengaturan->poin) }}"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            {{-- Preview --}}
            <div class="rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-700 dark:bg-amber-500/10 p-4">

                <h4 class="font-semibold text-amber-700 dark:text-amber-400 mb-2">
                    Preview Sistem
                </h4>

                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Setiap belanja
                    <b>Rp{{ number_format($pengaturan->minimal_belanja,0,',','.') }}</b>
                    mendapatkan
                    <b>{{ $pengaturan->poin }} poin</b>.
                </p>

            </div>

        </div>

        {{-- Footer --}}
        <div class="flex justify-end p-6 border-t border-gray-100 dark:border-gray-800">

            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition">

                Simpan Pengaturan

            </button>

        </div>

    </form>

</div>

@endsection