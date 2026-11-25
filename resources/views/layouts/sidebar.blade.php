@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="daisy-awning-stripes fixed flex flex-col mt-0 top-0 px-4 left-0 text-[#FAF5E8] h-screen transition-all duration-300 ease-in-out z-99999 border-r border-[#FAF5E8]/10 shadow-[4px_0_24px_-8px_rgba(35,24,21,0.35)]"
    x-data="{
        openSubmenus: {},
        init() {
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        @foreach ($item['subItems'] as $subItem)
                            if (currentPath === '{{ ltrim($subItem['path'], '/') }}' ||
                                window.location.pathname === '{{ $subItem['path'] }}') {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                        } @endforeach
                    @endif
                @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];

            if (newState) {
                this.openSubmenus = {};
            }

            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            return window.location.pathname === path || '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }" :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }" @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">

    <!-- Decorative strawberry watermark in bottom corner (clipped cleanly) -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden select-none">
        <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt=""
            class="absolute -bottom-6 -right-5 w-28 h-28 opacity-[0.07] -rotate-12">
        <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt=""
            class="absolute top-24 -right-6 w-20 h-20 opacity-[0.05] rotate-12">
    </div>

    <!-- Brand Logo Section (Desktop xl+ and Mobile Drawer Top) -->
    <div class="relative z-10 flex items-center justify-between pt-5 pb-5 border-b border-[#FAF5E8]/10"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
            'xl:justify-center px-0' : 'px-1.5'">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
            <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#FAF5E8] p-1.5 shadow-sm ring-2 ring-[#EACE80]/30">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="h-full w-full object-contain">
                </span>
                <div class="flex flex-col items-start leading-none">
                    <img src="{{ asset('images/logo/logo-wordmark-light.svg') }}" alt="Daisy Bakery"
                        class="h-7 w-auto object-contain">
                    <span class="mt-1 inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.16em] text-[#EACE80]/85">
                        Smart Bakery Management
                    </span>
                </div>
            </div>

            <div x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
                class="w-11 h-11 rounded-full bg-[#FAF5E8] flex items-center justify-center p-1.5 shadow-sm ring-2 ring-[#EACE80]/30">
                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="w-full h-full object-contain">
            </div>
        </a>

        <!-- Mobile Close Button inside Drawer -->
        <button type="button" @click="$store.sidebar.setMobileOpen(false)"
            class="xl:hidden flex items-center justify-center w-9 h-9 rounded-xl bg-[#FAF5E8]/10 text-[#FAF5E8] hover:bg-[#FAF5E8]/20 transition"
            aria-label="Tutup Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="relative z-10 flex flex-col flex-1 overflow-y-auto duration-300 ease-linear no-scrollbar pt-5 pb-6">
        <nav class="mb-6">
            <div class="flex flex-col gap-5">
                @foreach ($menuGroups as $groupIndex => $menuGroup)
                    <div>
                        <h2 class="mb-2.5 px-3 text-[10.5px] font-bold uppercase flex leading-[18px] text-[#EACE80]/70"
                            :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                'lg:justify-center px-0' : 'justify-start'">
                            <template
                                x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                                <span>{{ $menuGroup['title'] }}</span>
                            </template>
                            <template
                                x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                                <span class="w-5 h-0.5 rounded-full bg-[#EACE80]/30 my-2"></span>
                            </template>
                        </h2>

                        <ul class="flex flex-col gap-1">
                            @foreach ($menuGroup['items'] as $itemIndex => $item)
                                <li>
                                    @if (isset($item['subItems']))
                                        <button @click="toggleSubmenu({{ $groupIndex }}, {{ $itemIndex }})"
                                            class="menu-item group w-full" :class="[
                                                isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) ?
                                                'menu-item-active' : 'menu-item-inactive',
                                                !$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen ?
                                                'xl:justify-center px-2' : 'justify-start'
                                            ]">

                                            <span class="shrink-0" :class="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) ?
                                                'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <span
                                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="menu-item-text flex items-center gap-2 truncate">
                                                {{ $item['name'] }}
                                                @if (!empty($item['new']))
                                                    <span class="ml-auto" :class="isActive('{{ $item['path'] ?? '' }}') ?
                                                        'menu-dropdown-badge menu-dropdown-badge-active' :
                                                        'menu-dropdown-badge menu-dropdown-badge-inactive'">
                                                        new
                                                    </span>
                                                @endif
                                            </span>

                                            <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="ml-auto w-4 h-4 shrink-0 transition-transform duration-200" :class="{
                                                    'rotate-180 text-[#EACE80]': isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}),
                                                    'text-[#FAF5E8]/55': !isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                                }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <div
                                            x-show="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)">
                                            <ul class="mt-1.5 space-y-1 ml-5 pl-3.5 border-l border-[#FAF5E8]/15">
                                                @foreach ($item['subItems'] as $subItem)
                                                    <li>
                                                        <a href="{{ $subItem['path'] }}" class="menu-dropdown-item" :class="isActive('{{ $subItem['path'] }}') ?
                                                            'menu-dropdown-item-active' :
                                                            'menu-dropdown-item-inactive'">
                                                            <span class="truncate">{{ $subItem['name'] }}</span>
                                                            <span class="flex items-center gap-1 ml-auto">
                                                                @if (!empty($subItem['new']))
                                                                    <span :class="isActive('{{ $subItem['path'] }}') ?
                                                                        'menu-dropdown-badge menu-dropdown-badge-active' :
                                                                        'menu-dropdown-badge menu-dropdown-badge-inactive'">
                                                                        new
                                                                    </span>
                                                                @endif
                                                                @if (!empty($subItem['pro']))
                                                                    <span :class="isActive('{{ $subItem['path'] }}') ?
                                                                        'menu-dropdown-badge-pro menu-dropdown-badge-pro-active' :
                                                                        'menu-dropdown-badge-pro menu-dropdown-badge-pro-inactive'">
                                                                        pro
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <a href="{{ $item['path'] }}" class="menu-item group" :class="[
                                            isActive('{{ $item['path'] }}') ? 'menu-item-active' : 'menu-item-inactive',
                                            (!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                                            'xl:justify-center px-2' : 'justify-start'
                                        ]">
                                            <span class="shrink-0" :class="isActive('{{ $item['path'] }}') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <span
                                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="menu-item-text flex items-center gap-2 truncate">
                                                {{ $item['name'] }}
                                                @if (!empty($item['new']))
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#EACE80] text-[#3A221B]">
                                                        new
                                                    </span>
                                                @endif
                                            </span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>

        <!-- Sidebar Footer Card (Expanded) -->
        <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
            x-transition class="mt-auto pt-3">
            @include('layouts.sidebar-widget')
            <div class="rounded-2xl bg-[#FAF5E8]/[0.07] border border-[#FAF5E8]/10 px-3.5 py-3 flex items-center gap-3">
                <img src="{{ asset('images/decor/strawberry-3.svg') }}" alt="" class="w-8 h-8 shrink-0">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-[#FAF5E8] truncate">Daisy Bakery POS</p>
                    <p class="text-[11px] text-[#EACE80]/75 truncate">Freshly baked every day</p>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen" x-cloak @click="$store.sidebar.setMobileOpen(false)"
    class="fixed inset-0 z-9999 bg-[#231815]/60 backdrop-blur-[2px] xl:hidden"></div>
