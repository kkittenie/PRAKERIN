@extends('member.layout', ['title' => 'Masuk Member'])

@section('content')

    <div class="flex flex-col items-center pb-2 pt-10 text-center">
        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-theme-md">
            @include('member.components.icon-cup', ['class' => 'h-7 w-7'])
        </span>
        <h1 class="mt-4 text-title-sm font-bold text-gray-800">Selamat Datang Kembali</h1>
        <p class="mt-1 text-theme-sm text-gray-500">Masuk untuk cek poin dan tukar reward kamu.</p>
    </div>

    <form method="POST" action="{{ route('member.login.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="no_hp" class="mb-1.5 block text-theme-xs font-medium text-gray-600">Nomor HP</label>
            <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}" required autofocus placeholder="08xxxxxxxxxx"
                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-theme-sm text-gray-800 outline-none focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            @error('no_hp') <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-theme-xs font-medium text-gray-600">Kata Sandi</label>
            <input id="password" name="password" type="password" required
                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-theme-sm text-gray-800 outline-none focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            @error('password') <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-theme-sm text-gray-500">
            <input type="checkbox" name="ingat_saya" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            Ingat saya
        </label>

        <button type="submit" class="w-full rounded-xl bg-brand-600 py-3.5 text-theme-sm font-semibold text-white shadow-theme-md active:bg-brand-700">
            Masuk
        </button>
    </form>

    <p class="mt-5 text-center text-theme-sm text-gray-500">
        Belum punya akun member?
        <a href="{{ route('member.register') }}" class="font-semibold text-brand-600">Daftar sekarang</a>
    </p>

@endsection
