@php
    if ($item->status_pembayaran === 'Lunas') {
        $bayarBadge = ['label' => 'Lunas', 'class' => 'bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400'];
    } elseif ($item->bukti_dikonfirmasi_pada) {
        $bayarBadge = ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400'];
    } else {
        $bayarBadge = ['label' => 'Menunggu Pembayaran', 'class' => 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400'];
    }
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $bayarBadge['class'] }}">
    {{ $bayarBadge['label'] }}
</span>
