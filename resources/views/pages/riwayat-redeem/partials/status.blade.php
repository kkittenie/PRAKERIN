@php
    $statusBadge = [
        'menunggu_diambil' => ['label' => 'Menunggu Diambil', 'class' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'],
        'selesai' => ['label' => 'Selesai', 'class' => 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400'],
    ][$item->status];
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge['class'] }}">
    {{ $statusBadge['label'] }}
</span>
