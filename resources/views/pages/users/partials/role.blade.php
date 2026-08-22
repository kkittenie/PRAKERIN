@php
    $roleName = $item->roles->pluck('name')->first() ?? 'Tanpa Role';
@endphp
<span class="rounded-full bg-brand-50 dark:bg-brand-500/10 px-3 py-1 text-xs font-semibold text-brand-600 dark:text-brand-400">{{ $roleName }}</span>
