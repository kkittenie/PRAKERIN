@extends('layouts.fullscreen-layout')

@section('content')
<x-common.error-page
    code="500"
    image="500"
    accent="amber"
    title="Terjadi Kesalahan"
    message="Ada masalah di server kami. Tim sudah mendapat catatannya, coba lagi beberapa saat lagi."
>
    <button type="button" onclick="window.location.reload()"
        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.06] sm:w-auto">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15"/></svg>
        Muat Ulang
    </button>
</x-common.error-page>
@endsection