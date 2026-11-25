{{-- Reusable Google Sign-In Control (Mobile circular icon + Desktop pill matching reference) --}}
<div class="mt-6">
    {{-- Divider --}}
    <div class="relative flex items-center justify-center my-5">
        <div class="flex-1 border-t border-[#3C2415]/14"></div>
        <span class="px-3.5 text-[11.5px] font-medium text-[#8A766B] select-none">
            <span class="md:hidden">Or continue with</span>
            <span class="hidden md:inline">or</span>
        </span>
        <div class="flex-1 border-t border-[#3C2415]/14"></div>
    </div>

    {{-- Mobile: Circular floating Google icon button (matches mobile reference) --}}
    <div class="flex md:hidden justify-center">
        <a href="{{ route('member.google.redirect') }}" data-google-native
            aria-label="Continue with Google"
            class="group flex h-13 w-13 items-center justify-center rounded-full border border-[#3C2415]/12 bg-[#FFFDF9] shadow-[0_6px_18px_rgba(58,34,27,0.09)] transition hover:-translate-y-0.5 hover:border-[#8E1E26]/35 active:scale-95">
            <svg class="h-6 w-6" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.6 17.7 9.5 24 9.5z" />
                <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 7.1-10.1 7.1-17.5z" />
                <path fill="#FBBC05" d="M10.5 28.7A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.9-6.1A24 24 0 0 0 0 24c0 3.9.9 7.5 2.6 10.8l7.9-6.1z" />
                <path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.5-5.8c-2.1 1.4-4.9 2.3-8.4 2.3-6.3 0-11.6-4.1-13.5-9.8l-7.9 6.1C6.5 42.6 14.6 48 24 48z" />
            </svg>
        </a>
    </div>

    {{-- Desktop: Soft rounded-full pill button (matches desktop reference) --}}
    <div class="hidden md:block">
        <a href="{{ route('member.google.redirect') }}" data-google-native
            class="group flex w-full items-center justify-center gap-3 rounded-full border border-[#3C2415]/14 bg-[#FFFDF9] px-5 py-3.5 text-sm font-bold text-[#3A221B] shadow-[0_4px_14px_rgba(58,34,27,0.06)] transition hover:-translate-y-0.5 hover:border-[#8E1E26]/35 hover:bg-white active:scale-[0.99]">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.6 17.7 9.5 24 9.5z" />
                <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4.1 7.1-10.1 7.1-17.5z" />
                <path fill="#FBBC05" d="M10.5 28.7A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.9-6.1A24 24 0 0 0 0 24c0 3.9.9 7.5 2.6 10.8l7.9-6.1z" />
                <path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.5-5.8c-2.1 1.4-4.9 2.3-8.4 2.3-6.3 0-11.6-4.1-13.5-9.8l-7.9 6.1C6.5 42.6 14.6 48 24 48z" />
            </svg>
            <span>Continue with Google</span>
        </a>
    </div>
</div>
@include('mobile.auth._google-native')
