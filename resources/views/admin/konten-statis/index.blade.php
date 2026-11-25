@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Konten Statis" parentTitle="Data Master" />

<div class="space-y-6">

    <x-common.component-card title="Help Center, Terms & Conditions, Privacy Policy">

        @if (session('success'))
            <div class="mb-4 flex items-start gap-2 rounded-xl bg-green-50 p-3.5 text-sm text-green-700 dark:bg-green-500/10 dark:text-green-400">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 mt-0.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="m9 11 3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Kelola isi konten halaman informasi yang akan ditampilkan kepada pengguna member app.
        </p>

        @php
            $items = [
                [
                    'slug'  => 'help-center',
                    'label' => 'Help Center',
                    'icon'  => '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="currentColor" stroke-width="1.5"/>',
                    'desc'  => 'FAQ, panduan penggunaan, dan informasi bantuan untuk member.',
                ],
                [
                    'slug'  => 'terms-conditions',
                    'label' => 'Terms and Conditions',
                    'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.8"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
                    'desc'  => 'Syarat dan ketentuan penggunaan layanan Daisy.',
                ],
                [
                    'slug'  => 'privacy-policy',
                    'label' => 'Privacy Policy',
                    'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>',
                    'desc'  => 'Kebijakan privasi dan perlindungan data pengguna.',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ($items as $item)
                @php $k = $kontenList->get($item['slug']); @endphp
                <div class="flex flex-col rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/30 p-5 gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">{!! $item['icon'] !!}</svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $item['label'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">{{ $item['desc'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        @if ($k && $k->konten)
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 px-2.5 py-1 text-[11px] font-semibold">
                                <svg viewBox="0 0 24 24" fill="none" class="h-3 w-3"><path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Ada konten
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2.5 py-1 text-[11px] font-semibold">
                                <svg viewBox="0 0 24 24" fill="none" class="h-3 w-3"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Belum diisi
                            </span>
                        @endif

                        <a href="{{ route('konten-statis.edit', $item['slug']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-amber-700 hover:bg-amber-800 text-white text-xs font-semibold px-3.5 py-1.5 transition">
                            <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Edit Konten
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

    </x-common.component-card>

</div>

@endsection
