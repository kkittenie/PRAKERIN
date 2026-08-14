@extends('member.layout', ['title' => 'Settings'])

@section('content')

    <div class="-mt-6 flex flex-none items-center justify-between px-0 pt-6">
        <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800">
            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </a>
        <h1 class="text-base font-extrabold text-brand-800">Settings</h1>
        <span class="w-8"></span>
    </div>

    @if (session('success'))
        <p class="mt-4 rounded-xl bg-green-50 px-4 py-3 text-center text-xs font-semibold text-green-700">
            {{ session('success') }}
        </p>
    @endif

    <div class="mt-6 space-y-3">
        <a href="{{ route('member.settings.ubah-password') }}"
            onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
            class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 text-sm font-medium text-gray-800 shadow-xs transition active:scale-[0.99] hover:text-brand-800">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-25 text-brand-800">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="1.8" />
                        <path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </span>
                <span>Change Password</span>
            </div>
            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-gray-400">
                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
    </div>

@endsection