@php
    $statusBadge = [
        \App\Models\Transaksi::STATUS_DIPROSES => ['label' => 'Diproses', 'class' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'],
        \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => ['label' => 'Siap Diambil', 'class' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'],
        \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => ['label' => 'Sudah Diambil', 'class' => 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400'],
        \App\Models\Transaksi::STATUS_DIBATALKAN => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400'],
    ][$item->status_pesanan] ?? ['label' => '-', 'class' => 'bg-gray-100 text-gray-500'];
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge['class'] }}">
    {{ $statusBadge['label'] }}
</span>
