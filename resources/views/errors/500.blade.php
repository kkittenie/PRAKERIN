@extends('layouts.fullscreen-layout')

@section('content')
@php
    $currentYear = date('Y');
@endphp
  <div class="relative flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden z-1">
      {{-- common grid shape --}}
      <x-common.common-grid-shape />
      <!-- Centered Content -->
      <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
          <h1 class="mb-8 font-bold text-gray-800 text-title-md xl:text-title-2xl">
              TERJADI KESALAHAN
          </h1>

          <img src="{{ asset('images/error/500.svg') }}" alt="500" class="mx-auto dark:hidden" />
          <img src="{{ asset('images/error/500-dark.svg') }}" alt="500" class="mx-auto hidden dark:block" />

          <p class="mt-10 mb-6 text-base text-gray-700 sm:text-lg">
              Ada masalah di server kami. Tim sudah mendapat catatannya, coba lagi beberapa saat lagi.
          </p>

          <a href="{{ url('/') }}"
              class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800">
              Kembali ke Beranda
          </a>
      </div>
      <!-- Footer -->
      <p class="absolute text-sm text-center text-gray-500 -translate-x-1/2 bottom-6 left-1/2">
          &copy; {{ $currentYear }} - Saisho POS
      </p>
  </div>
@endsection
