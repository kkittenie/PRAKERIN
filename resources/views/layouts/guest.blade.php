<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-outfit text-gray-900 antialiased bg-gradient-to-br from-[#fcf8f3] via-[#f5ebe0] to-[#ebe0d3] min-h-screen flex flex-col justify-center items-center p-4 sm:p-6">
        <div class="w-full sm:max-w-md bg-white shadow-2xl overflow-hidden sm:rounded-3xl border border-[#5a3c27]/10 p-6 sm:p-8">
            <div class="flex flex-col items-center text-center mb-6">
                <a href="/" class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 border border-brand-200 text-brand-800 text-3xl shadow-sm mb-3">
                    🥐
                </a>
                <h1 class="text-xl font-extrabold text-brand-800 tracking-tight">Daisy Bakery POS</h1>
                <p class="text-xs text-gray-500 font-medium">Portal Admin & Kasir</p>
            </div>

            {{ $slot }}
        </div>
        
        <p class="text-center text-xs text-gray-400 mt-6">&copy; {{ date('Y') }} Daisy Bakery. All rights reserved.</p>
    </body>
</html>

