@php
    $authUser = auth()->user();
    $roleLabel = method_exists($authUser, 'getRoleNames') && $authUser->getRoleNames()->isNotEmpty()
        ? ucwords(str_replace(['_', '-'], ' ', $authUser->getRoleNames()->first()))
        : 'Staff';
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <button
        class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-full bg-[#FFFDF9] border border-[#3C2415]/15 hover:border-[#8E1E26]/35 hover:bg-white transition dark:bg-[#241A16] dark:border-white/10 dark:hover:bg-[#2E211C]"
        @click.prevent="toggleDropdown()" type="button">
        <span class="overflow-hidden rounded-full h-9 w-9 ring-2 ring-[#EACE80]/60 shrink-0">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($authUser->name) }}&background=8e1e26&color=faf5e8&bold=true"
                alt="{{ $authUser->name }}" class="w-full h-full object-cover" />
        </span>

        <span class="hidden sm:flex flex-col items-start text-left leading-tight mr-0.5">
            <span class="font-bold text-[13px] text-[#3A221B] dark:text-[#FAF5E8] max-w-[140px] truncate">{{ $authUser->name }}</span>
            <span class="text-[11px] font-semibold text-[#8E1E26] dark:text-[#EACE80]">{{ $roleLabel }}</span>
        </span>

        <svg class="w-4 h-4 transition-transform duration-200 text-[#8E1E26] dark:text-[#EACE80] shrink-0"
            :class="{ 'rotate-180': dropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="dropdownOpen" x-cloak x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-3 flex w-[270px] flex-col rounded-2xl border border-[#3C2415]/15 dark:border-white/10 bg-[#FFFDF9] dark:bg-[#241A16] p-3 shadow-[0_18px_36px_-10px_rgba(58,34,27,0.22)] z-50">

        <div class="relative overflow-hidden rounded-xl bg-[#FAF5E8] dark:bg-[#2E211C] px-3.5 py-3 border border-[#3C2415]/10 dark:border-white/10">
            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
                class="pointer-events-none absolute -right-2 -bottom-2 w-12 h-12 opacity-20">
            <div class="relative flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($authUser->name) }}&background=8e1e26&color=faf5e8&bold=true"
                    alt="{{ $authUser->name }}" class="w-10 h-10 rounded-full ring-2 ring-white dark:ring-[#241A16] shrink-0" />
                <div class="min-w-0">
                    <span class="block font-display font-bold text-[#3A221B] dark:text-[#FAF5E8] text-sm truncate">{{ $authUser->name }}</span>
                    <span class="mt-0.5 block text-xs text-[#7D6A60] dark:text-[#C5B5A8] truncate">{{ $authUser->email ?? '@' . $authUser->username }}</span>
                </div>
            </div>
        </div>

        <ul class="flex flex-col gap-1 pt-3 pb-2.5 border-b border-[#3C2415]/10 dark:border-white/10">
            <li>
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-3 py-2.5 font-semibold text-[#3A221B] dark:text-[#FAF5E8] rounded-xl group text-sm hover:bg-[#FCEEEF] hover:text-[#8E1E26] dark:hover:bg-white/5 dark:hover:text-[#EACE80] transition">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#FAF5E8] dark:bg-[#2E211C] text-[#8E1E26] dark:text-[#EACE80] group-hover:bg-white dark:group-hover:bg-[#3A221B] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    Profil Saya
                </a>
            </li>
        </ul>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center w-full gap-3 px-3 py-2.5 mt-2 font-semibold text-[#8E1E26] dark:text-[#F09A9E] rounded-xl group text-sm hover:bg-[#FCEEEF] dark:hover:bg-[#8E1E26]/20 transition"
                @click="closeDropdown()">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#FCEEEF] dark:bg-[#8E1E26]/20 text-[#8E1E26] dark:text-[#F09A9E]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                </span>
                Keluar
            </button>
        </form>
    </div>
</div>
