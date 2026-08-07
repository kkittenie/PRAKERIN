<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Login &middot; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-outfit antialiased">
    <div class="mx-auto flex min-h-screen max-w-md flex-col bg-white px-6 pb-8 pt-4 shadow-[0_0_60px_-15px_rgba(90,60,39,0.15)]">
        
        {{-- Header Bar --}}
        <div class="relative flex items-center justify-center py-2">
            <a href="{{ route('member.welcome') }}" class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <h1 class="text-base font-bold text-brand-800">Login</h1>
        </div>

        {{-- Flash messages --}}
        @if (session('error') || $errors->any())
            <div class="mt-4 flex items-start gap-2 rounded-xl bg-red-50 p-3.5 text-xs text-red-700">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 mt-0.5"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('error') ?? $errors->first() }}</span>
            </div>
        @endif

        {{-- Main Content --}}
        <div class="mt-8 flex-1">
            <h2 class="text-lg font-bold text-brand-800">Input Phone Number</h2>

            <form method="POST" action="{{ route('member.login.store') }}" class="mt-6 space-y-6">
                @csrf

                {{-- Phone Input Group matching Fore UI --}}
                <div>
                    <div class="relative flex items-center border-b-2 border-brand-800 pb-2 pt-1 focus-within:border-brand-800">
                        <div class="flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-800">
                            <span>+62</span>
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-500"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}" required autofocus placeholder="882000768044"
                               class="ml-3 flex-1 bg-transparent text-base font-semibold text-gray-900 outline-none placeholder:text-gray-400 placeholder:font-normal">
                        <button type="button" onclick="document.getElementById('no_hp').value=''" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    @error('no_hp') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                {{-- Password Field --}}
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Kata Sandi</label>
                    <input id="password" name="password" type="password" required placeholder="Masukkan kata sandi"
                           class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm text-gray-900 outline-none focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                    @error('password') <p class="mt-1.5 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs font-medium text-gray-600 cursor-pointer">
                        <input type="checkbox" name="ingat_saya" class="rounded border-gray-300 text-brand-800 focus:ring-brand-800">
                        Ingat saya
                    </label>
                    <a href="{{ route('member.register') }}" class="text-xs font-bold text-brand-800 hover:underline">Belum punya akun? Daftar</a>
                </div>

                {{-- Continue Button --}}
                <button type="submit" class="w-full rounded-2xl bg-brand-800 py-3.5 text-center text-base font-semibold text-white shadow-md transition hover:bg-brand-900 active:scale-[0.99]">
                    Continue
                </button>
            </form>
        </div>

        {{-- Bottom Terms Notice --}}
        <p class="text-center text-xs leading-relaxed text-gray-500 mt-6">
            By signing in to Saisho app, you agree to the <a href="#" class="font-bold text-brand-800 hover:underline">Terms & Condition</a> and <a href="#" class="font-bold text-brand-800 hover:underline">Privacy Policy</a>
        </p>

    </div>
</body>
</html>
