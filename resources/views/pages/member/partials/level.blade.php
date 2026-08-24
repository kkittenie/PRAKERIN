@php
    $levelClasses = match ($item->warna_badge_level) {
        'yellow' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-300',
        'amber' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400',
        'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        'green' => 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
        'purple' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
        'red' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
        'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
        default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };
@endphp
<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $levelClasses }}">
    {{ $item->level }}
</span>
